<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Welcome extends Controller
{
	public function index()
	{
		$data = [];

		$user_email = $this->session->userdata('user_email');

		if ($user_email) {
			$data['is_logged_in'] = true;
			$data['user_email'] = $user_email;
		} else {
			$data['is_logged_in'] = false;
		}

		$this->call->view('user_landing', $data);
	}
}
