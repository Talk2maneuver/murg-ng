const db = require('../config/database');

/**
 * Typed error for auth repository failures — allows the controller to log
 * the real reason without exposing SQL internals to the HTTP response.
 */
class AuthRepositoryError extends Error {
  constructor(code, message, cause) {
    super(message);
    this.name = 'AuthRepositoryError';
    this.code = code; // e.g. 'DB_QUERY_FAILED', 'MISSING_COLUMNS'
    if (cause) this.cause = cause;
  }
}

/**
 * AuthRepository — Database operations for authentication.
 * Never exposes password/password_hash in returned user objects by default.
 */
class AuthRepository {
  /**
   * Find a user by email for login.
   * Returns full row including password/password_hash for verification.
   */
  async findByEmailForAuth(email) {
    try {
      const [rows] = await db.query(
        'SELECT id, facilityID, name, fname, email, phone, role, status, password, password_hash, permissions FROM facility WHERE email = ? LIMIT 1',
        [email]
      );
      return rows[0] || null;
    } catch (err) {
      // Surface a useful error code rather than a raw SQL exception
      if (err.code === 'ER_BAD_FIELD_ERROR') {
        const missing = err.sqlMessage || err.message;
        throw new AuthRepositoryError(
          'MISSING_COLUMNS',
          `Required column missing in 'facility' table. Run migration: database/migrations/001_multibranch_and_stock_ledger.sql — SQL error: ${missing}`
        );
      }
      throw new AuthRepositoryError('DB_QUERY_FAILED', 'Database query failed during user lookup', err);
    }
  }

  /**
   * Upgrade legacy MD5 password to bcrypt (transparent background upgrade).
   * Failures here are non-fatal and logged as warnings only.
   */
  async upgradeToBcrypt(userId, bcryptHash) {
    await db.query(
      'UPDATE facility SET password_hash = ? WHERE id = ?',
      [bcryptHash, userId]
    );
  }

  /**
   * Get safe user profile by ID (no passwords).
   */
  async findById(userId) {
    const [rows] = await db.query(
      'SELECT id, facilityID, name, fname, email, phone, role, status, permissions FROM facility WHERE id = ? LIMIT 1',
      [userId]
    );
    return rows[0] || null;
  }
}

module.exports = new AuthRepository();
module.exports.AuthRepositoryError = AuthRepositoryError;
