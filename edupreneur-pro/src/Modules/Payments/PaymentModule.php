<?php
namespace EdupreneurPro\Modules\Payments;
use EdupreneurPro\Core\Modules\ModuleInterface;
class PaymentModule implements ModuleInterface {
	public function init() {}
	public function get_id() { return 'payments'; }
}
