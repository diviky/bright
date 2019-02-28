<?php

namespace Karla\Traits;

trait Message
{
    public function message($result = true, $action = 'save', $name = 'row')
    {
        if ($result) {
            $response = [
                'status'  => 'OK',
                'message' => __(':Name :action successfully', ['name' => $name, 'action' => $action . 'd']),
            ];

            if (!is_bool($result)) {
                $response['id'] = $result;
            }

            return $response;
        }

        return [
            'status'  => 'ERROR',
            'message' => __('Unable to :action :name. Please try again.', ['name' => $name, 'action' => $action]),
        ];
    }

    public function deleted($result = true, $name = 'row')
    {
        return $this->message($result, 'delete', $name);
    }

    public function inserted($result = true, $name = 'row')
    {
        return $this->message($result, 'save', $name);
    }

    public function updated($result = true, $name = 'row')
    {
        return $this->message($result, 'update', $name);
    }
}
