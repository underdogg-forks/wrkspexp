<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => trans('enums.quote_status.draft'),
            self::Sent => trans('enums.quote_status.sent'),
            self::Accepted => trans('enums.quote_status.accepted'),
            self::Declined => trans('enums.quote_status.declined'),
            self::Expired => trans('enums.quote_status.expired'),
        };
    }
}
