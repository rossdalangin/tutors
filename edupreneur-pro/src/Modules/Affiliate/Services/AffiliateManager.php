<?php
namespace EdupreneurPro\Modules\Affiliate\Services;

use EdupreneurPro\Modules\Affiliate\Repositories\AffiliateRepository;

class AffiliateManager {
	private $repository;
	public function __construct() {
		$this->repository = new AffiliateRepository();
	}
	public function register_affiliate( $user_id ) {
		return $this->repository->create( array(
			'user_id'       => $user_id,
			'referral_code' => wp_generate_password( 8, false ),
			'status'        => 'active',
		) );
	}
}
