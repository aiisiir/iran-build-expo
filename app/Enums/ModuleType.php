<?php

namespace App\Enums;

enum ModuleType: string
{
    case Posts = 'posts';
    case Links = 'links';
    case Content = 'content';
    case Gallery = 'gallery';
    case Faq = 'faq';
    case Companies = 'companies';
    case Embed = 'embed';
}
