<?php

namespace App\Http\Controllers\API;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "Dokumentasi API untuk RuangSadar",
    title: "RuangSadar API"
)]
#[OA\Server(
    url: "https://ruang-sadar-production.up.railway.app/", 
    description: "Production Server (Railway)"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer"
)]
class OpenApiSpec
{
    // Fungsi test() sudah dihapus supaya API lebih bersih
}