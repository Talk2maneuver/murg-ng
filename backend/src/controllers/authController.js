const jwt = require('jsonwebtoken');
const authRepo = require('../repositories/authRepository');
const { AuthRepositoryError } = require('../repositories/authRepository');
const { verifyPassword, hashPassword } = require('../utils/passwordUtils');
const { success, error, unauthorized } = require('../utils/responseUtils');

const JWT_SECRET = process.env.JWT_SECRET || 'murg_fallback_secret';
const JWT_EXPIRES_IN = process.env.JWT_EXPIRES_IN || '7d';

class AuthController {
  async login(req, res, next) {
    try {
      const { email, password } = req.body;

      if (!email || !password) {
        return error(res, 'Email and password are required', 400);
      }

      // --- Step 1: User lookup ---
      let user;
      try {
        user = await authRepo.findByEmailForAuth(email.trim().toLowerCase());
      } catch (err) {
        if (err instanceof AuthRepositoryError) {
          // Logs the real DB reason internally — NEVER sent to the browser
          console.error(`[AUTH_LOGIN_FAILED] DB error during user lookup — code: ${err.code} — ${err.message}`);
        } else {
          console.error('[AUTH_LOGIN_FAILED] Unexpected error during user lookup:', err.code || err.message);
        }
        return unauthorized(res, 'Invalid email or password');
      }

      if (!user) {
        // Safe internal log — does NOT log the email in production to avoid PII exposure in logs
        const logEmail = process.env.NODE_ENV === 'development' ? email.trim().toLowerCase() : '[redacted]';
        console.warn(`[AUTH_LOGIN_FAILED] user_not_found — email: ${logEmail}`);
        return unauthorized(res, 'Invalid email or password');
      }

      // --- Step 2: Account status check ---
      if (user.status !== 1) {
        console.warn(`[AUTH_LOGIN_FAILED] account_suspended — user_id: ${user.id} email: ${user.email}`);
        return unauthorized(res, 'Account suspended. Contact system administrator.');
      }

      // --- Step 3: Password verification ---
      const { valid, needsUpgrade, method } = await verifyPassword(password, user);
      if (!valid) {
        console.warn(`[AUTH_LOGIN_FAILED] password_mismatch — user_id: ${user.id} method: ${method}`);
        return unauthorized(res, 'Invalid email or password');
      }

      // --- Step 4: Transparently upgrade legacy MD5 hash to bcrypt ---
      if (needsUpgrade) {
        try {
          const bcryptHash = await hashPassword(password);
          await authRepo.upgradeToBcrypt(user.id, bcryptHash);
          console.log(`[AUTH] Upgraded password hash from MD5 to bcrypt for user_id: ${user.id}`);
        } catch (err) {
          // Non-fatal: upgrade failure does not block login
          console.error('[AUTH] Failed to upgrade password to bcrypt for user_id:', user.id, '—', err.message);
        }
      }

      // --- Step 5: Build permissions ---
      let permissions;
      try {
        permissions = user.permissions
          ? JSON.parse(user.permissions)
          : (user.role === 'Admin' ? ['*'] : []);
      } catch (err) {
        // Corrupt JSON in permissions column — fall back gracefully
        console.error(`[AUTH] Corrupt permissions JSON for user_id: ${user.id} — falling back to role-based default. Raw: ${user.permissions}`);
        permissions = user.role === 'Admin' ? ['*'] : [];
      }

      // --- Step 6: Generate JWT ---
      const token = jwt.sign(
        {
          sub: user.id,
          name: user.name,
          email: user.email,
          role: user.role,
          facilityID: user.facilityID,
          isGlobalAdmin: user.role === 'Admin',
          permissions,
        },
        JWT_SECRET,
        { expiresIn: JWT_EXPIRES_IN }
      );

      // Safe user object for client (no passwords, no hashes)
      const safeUser = {
        id: user.id,
        name: user.name,
        fname: user.fname,
        email: user.email,
        phone: user.phone,
        role: user.role,
        facilityID: user.facilityID,
        isGlobalAdmin: user.role === 'Admin',
        permissions,
      };

      console.log(`[AUTH] Login successful — user_id: ${user.id} role: ${user.role} facilityID: ${user.facilityID}`);
      return success(res, { token, user: safeUser }, 'Login successful');
    } catch (err) {
      console.error('[AUTH_LOGIN_FAILED] Unhandled exception:', err.message);
      next(err);
    }
  }

  async me(req, res, next) {
    try {
      const user = await authRepo.findById(req.user.id);
      if (!user) {
        console.warn(`[AUTH] /me — user_not_found for user_id: ${req.user.id}`);
        return unauthorized(res, 'User not found');
      }

      let permissions;
      try {
        permissions = user.permissions
          ? JSON.parse(user.permissions)
          : (user.role === 'Admin' ? ['*'] : []);
      } catch (e) {
        permissions = user.role === 'Admin' ? ['*'] : [];
      }

      return success(res, {
        ...user,
        isGlobalAdmin: user.role === 'Admin',
        permissions,
      });
    } catch (err) {
      next(err);
    }
  }
}

module.exports = new AuthController();
