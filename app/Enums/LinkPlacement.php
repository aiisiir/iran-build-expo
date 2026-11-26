<?php

namespace App\Enums;

enum LinkPlacement: string
{
    case Menu = 'menu';
    case Logo = 'logo';
    case QuickLinks = 'quick_links';
    case Organizers = 'organizers';
    case Supporters = 'supporters';
    case Footer = 'footer';
}
