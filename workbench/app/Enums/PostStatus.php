<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}

// enum PostStatus
// {
//     case Draft;
//     case Published;
//     case Archived;
// }
