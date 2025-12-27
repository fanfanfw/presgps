<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class AdminResetPasswordNotification extends ResetPassword
{
    /**
     * Get the reset URL for the given notifiable (admin/HRD flow).
     *
     * @param  mixed  $notifiable
     */
    protected function resetUrl($notifiable)
    {
        return url(route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}

