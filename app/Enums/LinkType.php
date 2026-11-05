<?php

namespace App\Enums;

enum LinkType: string
{
    case Page = 'page';
    case Url = 'url';
    case File = 'file';
}
