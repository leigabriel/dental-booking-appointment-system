<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

class Welcome extends Controller
{
	public function index()
	{
		$data = [];

		// Prefer the central is_logged_in flag for checking login state.
		$is_logged_in = $this->session->userdata('is_logged_in');
		$data['is_logged_in'] = (bool) $is_logged_in;
		$data['user_email'] = $this->session->userdata('user_email') ?? null;
		$data['username'] = $this->session->userdata('username') ?? null;

		$this->call->view('user_landing', $data);
	}
}
