<?php

namespace Karla\Mail;

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

        $from = $this->getMailfrom($user_id) ?: config('app.name');

        $data['subject'] = \str_replace(['_sitename_', ':app'], [$from, $from], $data['subject']);

        $template = $data['template'];

        $mail = (new Mailable())
            ->subject($data['subject'])
            ->with($data['with']);

        if ($data['cc']) {
            $mail->cc($data['cc']);
        }

        if ($data['prefix']) {
            $mail->prefix($data['prefix']);
        }

        if ($data['bcc']) {
            $mail->bcc($data['bcc']);
        }

        $mail->markdown($template);

        if ($from) {
            $mail->from(config('mail.from.address'), $from);
        }

        return $mail->deliver($to, true);
    }

    protected function getMailTo($user_id = null)
    {
        if (empty($user_id)) {
            return;
        }

        return $this->db->table('auth_users')
            ->where('id', $user_id)
            ->first(['email', 'name']);
    }

    protected function getBranding($user_id = null)
    {
        if (empty($user_id)) {
            return;
        }

        $parent_id = $this->db->table('auth_users')
            ->where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            $row = $this->db->table('app_branding')
                ->where('user_id', $parent_id)
                ->first();
        }
    }

    protected function getMailFrom($user_id = null)
    {
        if (empty($user_id)) {
            return;
        }

        $parent_id = $this->db->table('auth_users')
            ->where('id', $user_id)
            ->value('parent_id');

        if ($parent_id) {
            return $this->db->table('app_branding')
                ->where('user_id', $parent_id)
                ->value('name');
        }
    }
}
