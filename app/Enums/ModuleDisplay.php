<?php

namespace App\Enums;

enum ModuleDisplay: string
{
    case List = 'list';
    case Grid = 'grid';
    case Slider = 'slider';
    case Auto = 'auto';
    case Hero = 'hero'; // home page only: the first four posts become the hero slides
}
