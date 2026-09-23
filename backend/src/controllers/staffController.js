const staffRepo = require('../repositories/staffRepository');
const { hashPassword, md5Hash } = require('../utils/passwordUtils');
const { success, created, error, notFound } = require('../utils/responseUtils');

class StaffController {
  async list(req, res, next) {
    try {
      const facilityID = req.user.isGlobalAdmin ? (req.query.branchId || null) : req.user.facilityID;
      const role = req.query.role || null;
      const staffList = await staffRepo.findAll({ facilityID, role });
      return success(res, staffList);
    } catch (err) {
      next(err);
    }
  }

  async get(req, res, next) {
    try {
      const facilityID = req.user.isGlobalAdmin ? null : req.user.facilityID;
      const staff = await staffRepo.findById(req.params.id, facilityID);
      if (!staff) {
        return notFound(res, 'Staff member not found');
      }
      return success(res, staff);
    } catch (err) {
      next(err);
    }
  }

  async create(req, res, next) {
    try {
      const { facilityID, name, email, phone, gender, role, password, address, fname } = req.body;

      if (!name || !email || !password || !role || !facilityID) {
        return error(res, 'Name, email, password, role, and branch are required', 400);
      }

      const exists = await staffRepo.emailExists(email.trim().toLowerCase());
      if (exists) {
        return error(res, 'Email address is already in use', 409);
      }

      const passwordHash = await hashPassword(password);
      const legacyPassword = md5Hash(password);
      const staffId = await staffRepo.create({
        facilityID,
        name: name.trim(),
        email: email.trim().toLowerCase(),
        phone: phone || '',
        gender: gender || 'Male',
        role,
        passwordHash,
        legacyPassword,
        address: address || '',
        fname: fname || name,
      });

      return created(res, { id: staffId }, 'Staff member created successfully');
    } catch (err) {
      next(err);
    }
  }

  async updateRole(req, res, next) {
    try {
      const { role, facilityID } = req.body;
      if (!role || !facilityID) {
        return error(res, 'Role and branch are required', 400);
      }

      const updated = await staffRepo.updateRole(req.params.id, { role, facilityID });
      if (!updated) {
        return notFound(res, 'Staff member not found');
      }
      return success(res, null, 'Staff role and branch updated successfully');
    } catch (err) {
      next(err);
    }
  }

  async toggleStatus(req, res, next) {
    try {
      const { status } = req.body;
      if (status !== 0 && status !== 1) {
        return error(res, 'Status must be 0 (suspended) or 1 (active)', 400);
      }

      const updated = await staffRepo.setStatus(req.params.id, status);
      if (!updated) {
        return notFound(res, 'Staff member not found');
      }
      return success(res, null, `Staff member ${status === 1 ? 'activated' : 'suspended'} successfully`);
    } catch (err) {
      next(err);
    }
  }
}

module.exports = new StaffController();
