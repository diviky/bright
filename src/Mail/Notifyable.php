<?php

declare(strict_types=1);

namespace Diviky\Bright\Mail;

use App\Models\User;
use Diviky\Bright\Models\Models;

trait Notifyable
{
    /**
     * Helper method to send emails.
     *
     * @param  array  $data
     * @param  null|int  $user_id
     * @return bool
     */
    public function sendAnEmail($data = [], $user_id = null)
    {
        if (empty($data['to'])) {
            $to = $this->getMailTo($user_id);
        } else {
            $to = $data['to'];
        }

        if (empty($to)) {
            return false;
        }

        $from = $this->getMailfrom($user_id);

        if (isset($from)) {
            $data['subject'] = \str_replace('_sitename_', $from, $data['subject']);
        }

        $mail = (new Mailable())
            ->subject($data['subject'])
            ->with($data['with']);

        if (isset($data['prefix'])) {
            $mail->prefix($data['prefix']);
        }

        if (isset($data['cc'])) {
            $cc = \is_string($data['cc']) ? \explode(',', $data['cc']) : $data['cc'];

            $mail->cc($cc);
        }

        if (isset($data['bcc'])) {
            $bcc = \is_string($data['bcc']) ? \explode(',', $data['bcc']) : $data['bcc'];

            $mail->bcc($bcc);
        }

        if (isset($data['replyTo'])) {
            $mail->replyTo($data['replyTo']);
        }

        if (isset($data['file'])) {
            $files = \is_string($data['file']) ? \explode(',', $data['file']) : $data['file'];

            foreach ($files as $file) {
                $mail->attach($file);
            }
        }

        $mail->markdown($data['template']);

        if (isset($from)) {
            $mail->from(config('mail.from.address'), $from);
        }

        return $mail->deliver($to, true);
    }

    /**
     * Get the mail to address from user id.
     *
     * @param  null|int  $user_id
     * @return null|object
     */
    protected function getMailTo($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        return User::where('id', $user_id)
            ->first(['email', 'name']);
    }

    /**
     * Get the user branding details.
     *
     * @param  null|int  $user_id
     * @return null|object
     */
    protected function getBranding($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        $parent_id = User::where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            return Models::branding()::where('user_id', $parent_id)
                ->first();
        }

        return null;
    }

    /**
     * Get the mail from details from user id.
     *
     * @param  null|int  $user_id
     * @return null|string
     */
    protected function getMailFrom($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        $parent_id = User::where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            return Models::branding()::where('user_id', $parent_id)
                ->value('name');
        }

        return null;
    }

    /**
     * print info.
     *
     * @param  null|string  $message
     */
    protected function notify($message): void
    {
        echo '[' . \date('d/m/Y H:i:s') . '] ' . $message . "\n";
    }
}
