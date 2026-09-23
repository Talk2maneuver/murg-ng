const express = require('express');
const router = express.Router();
const salesController = require('../controllers/salesController');
const { authenticate, requireBranchScope } = require('../middleware/auth');

router.use(authenticate);
router.use(requireBranchScope);

router.get('/', salesController.list);
router.post('/checkout', salesController.checkout);
router.get('/:orderId/items', salesController.getOrderItems);
router.get('/:orderId/receipt', salesController.getReceipt);

module.exports = router;
