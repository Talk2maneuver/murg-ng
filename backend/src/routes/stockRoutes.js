const express = require('express');
const router = express.Router();
const stockController = require('../controllers/stockController');
const {
  authenticate,
  requireBranchScope,
  requireAdminPriceControl,
} = require('../middleware/auth');

router.use(authenticate);
router.use(requireBranchScope);

router.get('/', stockController.list);
router.get('/stores', stockController.getStores);
router.get('/movements', stockController.getMovements);
router.get('/:id', stockController.get);
router.patch('/:id/price', requireAdminPriceControl, stockController.updatePrice);
router.patch('/:id/yard-config', requireAdminPriceControl, stockController.updateYardConfig);
router.post('/receive', stockController.receiveStock);

module.exports = router;
