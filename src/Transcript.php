<?php

declare(strict_types=1);

namespace App;

enum Transcript: string
{

    case Retrieved = 'Retrieved';
    case NotRetrieved = 'Not Retrieved';
    case NotAvailable = 'Not Available';

}
