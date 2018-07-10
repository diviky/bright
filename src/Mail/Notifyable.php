<?php

namespace Karla\Mail;

use Karla\Mail\Mailable;

trait Notifyable
{
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

        $data['subject'] = str_replace(['_sitename_'], [$from], $data['subject']);

        $mail = (new Mailable())
            ->subject($data['subject'])
            ->with($data['with'])
            ->markdown($data['template']);

        if ($from) {
            $mail->from(config('mail.from.address'), $from);
        }

        return $mail->deliver($to, true);
    }

    protected function getMailTo($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        return $this->table('auth_users')
            ->where('id', $user_id)
            ->first(['email', 'name']);
    }

    protected function getBranding($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        $parent_id = $this->table('auth_users')
            ->where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            $row = $this->table('app_branding')
                ->where('user_id', $parent_id)
                ->first();
        }

        return null;
    }

    protected function getMailFrom($user_id = null)
    {
        if (empty($user_id)) {
            return null;
        }

        $parent_id = $this->table('auth_users')
            ->where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            return $this->table('app_branding')
                ->where('user_id', $parent_id)
                ->value('name');
        }

        return null;
    }
}
