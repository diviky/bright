<?php

namespace Diviky\Bright\Traits;

trait Message
{
    /**
     * @psalm-return array{status: string, code: 200|500, message: array|null|string, id?: mixed}
     *
     * @param mixed $result
     * @param string $action
     * @param string $name
     * @param mixed $id
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function message($result = true, $action = 'save', $name = 'row', $id = true): array
    {
        if ($result) {
            $response = [
                'status'  => 'OK',
                'code'    => 200,
                'message' => __(':Name :action successfully', ['name' => $name, 'action' => $action . 'd']),
            ];

            if ($id && !\is_bool($result)) {
                $response['id'] = $result;
            }

            return $response;
        }

        return [
            'status'  => 'ERROR',
            'code'    => 500,
            'message' => __('Unable to :action :name. Please try again.', ['name' => $name, 'action' => $action]),
        ];
    }

    /**
     * Delete message.
     *
     * @param bool   $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function deleted($result = true, $name = 'row')
    {
        return $this->message($result, 'delete', $name, false);
    }

    /**
     * Insert message.
     *
     * @param bool   $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function inserted($result = true, $name = 'row')
    {
        return $this->message($result, 'save', $name);
    }

    /**
     * Updated message.
     *
     * @param bool   $result
     * @param string $name
     *
     * @return (array|int|mixed|null|string)[]
     */
    public function updated($result = true, $name = 'row')
    {
        return $this->message($result, 'update', $name, false);
    }
}
