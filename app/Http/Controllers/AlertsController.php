<?php


namespace App\Http\Controllers;

use App\Http\Models\Alerts;

/**
 * Class AlertsController
 * @package App\Http\Controllers
 */
class AlertsController extends Controller
{

	/**
	 * @return string
	 */
	public function index()
	{
		$alerts = Alerts::orderBy('id', 'desc')->take(30)->get();

		return view('pages.alerts', compact('alerts'));
	}
}
