<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="Ipoh Drum Laravel API",
 *     version="1.0",
 *     description="This is a swagger-generated API documentation for the project Ipoh Drum.",
 *     @SWG\Contact(
 *         email=["siansiong5@gmail.com", "henry_lcz97@hotmail.com"]
 *     )
 *   )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
