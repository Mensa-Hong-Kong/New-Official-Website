<?php

namespace App\Models;

use App\Models\Verification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmail;

class UserHasContact extends Model
{
    public function sendVerifyCode()
    {
        switch ($this->type) {
            case 'email':
                $this->notify(new VerifyEmail($this->newVerifyCode()));
                break;
            case 'mobile':
                $this->notify(new VerifyMobile($this->newVerifyCode()));
                break;
        }
    }

    public function routeNotificationForMail(): array
    {
        return [$this->email => $this->user->given_name];
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->mobile;
    }
}
