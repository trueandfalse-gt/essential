<?php
namespace Trueandfalse\Essential\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'tenants';
}
