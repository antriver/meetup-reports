<?php

namespace Meetup;

use Carbon\Carbon;
use PDO;

class Events extends AbstractDataSource
{
    public function all()
    {
        $query = $this->db->prepare('SELECT * FROM events ORDER BY created DESC');
        $query->execute();

        $rows = $query->fetchAll(PDO::FETCH_OBJ);

        return $rows;
    }

    public function rsvpUpdateNeeded()
    {
        $time = (new \DateTime('-7 DAYS'))->format('Y-m-d H:i:s');

        $query = $this->db->prepare(
            'SELECT * FROM events WHERE rsvps_updated_at IS NULL OR time > ? ORDER BY created DESC'
        );
        $query->execute([
            $time
        ]);

        $rows = $query->fetchAll(PDO::FETCH_OBJ);

        return $rows;
    }

    public function setRsvpsUpdatedAt($eventId)
    {
        $query = $this->db->prepare('UPDATE events SET rsvps_updated_at = NOW() WHERE id = ?');
        $query->execute([$eventId]);

        return $query->rowCount() > 0;
    }

    public function insertEvent($data)
    {
        $keys = [
            'id',
            'name',
            'time',
            'status',
            'visibility',
            //'venue',
            'created',
            'headcount',
            'waitlist_count',
            'maybe_rsvp_count',
            'description',
            'yes_rsvp_count',
        ];

        $dates = [
            'time',
            'created',
        ];

        return $this->insert('events', $keys, $dates, $data);
    }

    public function update()
    {
        $page = 40;
        $offset = 0;

        while (true) {
            $response = $this->request(
                '/2/events',
                [
                    'page' => $page,
                    'offset' => $offset,
                    'group_urlname' => $this->config['groupUrl'],
                    'status' => 'upcoming,past,proposed,suggested,cancelled,draft',
                ]
            );

            echo "{$offset}\t".count($response->results).PHP_EOL;

            foreach ($response->results as $result) {
                echo "{$result->id}\t{$result->name}";

                if (!empty($result->time)) {
                    echo "\t".Carbon::createFromTimestamp($result->time / 1000)->toDateTimeString();
                }

                echo PHP_EOL;

                $this->insertEvent($result);
            }

            if (!$response->meta->next) {
                break;
            }

            ++$offset;
        }
    }
}
