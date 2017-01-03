<?php

namespace Meetup;

class Members extends AbstractDataSource
{
    public function clear()
    {
        $this->db->exec('DELETE FROM members');
    }

    public function insertMember($data)
    {
        $keys = [
            'id',
            'name',
            'status',
            'joined',
            'city',
            'country',
            'state',
            'type',
        ];

        $dates = [
            'joined',
        ];

        return $this->insert('members', $keys, $dates, $data);
    }

    public function update()
    {
        $this->clear();

        $page = 40;
        $offset = 0;
        while (true) {
            $response = $this->request(
                '/'.$this->config['groupUrl'].'/members',
                [
                    'page' => $page,
                    'offset' => $offset,
                ]
            );

            echo "{$offset}\t".count($response).PHP_EOL;

            foreach ($response as $result) {
                echo "{$result->id}\t{$result->name}".PHP_EOL;

                $this->insertMember($result);
            }

            if (count($response) < $page) {
                break;
            }

            ++$offset;
        }
    }
}
