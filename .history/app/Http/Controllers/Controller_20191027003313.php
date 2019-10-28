<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @SWG\Swagger(
 *     host="localhost:8000",
 *     basePath="/api",
 *   @SWG\Info(
 *     title="Ipoh Drum Laravel API",
 *     version="1.0",
 *     description="This is a swagger-generated API documentation for the project Ipoh Drum.",
 *     @SWG\Contact(
 *         email="henry_lcz97@hotmail.com"
 *     )
 *   )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
