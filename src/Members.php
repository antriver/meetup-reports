<?php

namespace Meetup;

use PDO;

class Members extends AbstractDataSource
{
    public function all()
    {
        $query = $this->db->prepare('SELECT * FROM members ORDER BY joined_group DESC');
        $query->execute();

        $rows = $query->fetchAll(PDO::FETCH_OBJ);

        return $rows;
    }

    public function insertMember($data)
    {
        $keys = [
            'id',
            'name',
            'status',
            'joined',
            'joined_group',
            'city',
            'country',
            'state',
            'type',
        ];

        $dates = [
            'joined',
        ];

        $data->joined_group = $data->group_profile->created;

        return $this->insert('members', $keys, $dates, $data);
    }

    public function update()
    {
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
