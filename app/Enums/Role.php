<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'superadmin'; // everything an admin can, plus custom code and who may be superadmin
    case Admin = 'admin';
    case Editor = 'editor';
}
