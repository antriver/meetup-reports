<?php

namespace Meetup;

class Rsvps extends AbstractDataSource
{
    public function update()
    {
        $events = $this->meetup->events->rsvpUpdateNeeded();

        foreach ($events as $event) {
            echo "{$event->id}\t{$event->name}".PHP_EOL;

            try {
                // To avoid throttling, only make 1 request per second:
                sleep(1);
                $rsvps = $this->getRsvpsForEvent($event);
            } catch (\Exception $e) {
                echo "\t!!! Unable to get RSVPs {$e->getMessage()}";
                continue;
            }

            $this->meetup->events->setRsvpsUpdatedAt($event->id);

            if (empty($rsvps)) {
                continue;
            }

            foreach ($rsvps as $rsvp) {
                echo "\t{$rsvp->event->id}\t{$rsvp->event->name}\t{$rsvp->member->id}";
                echo "\t{$rsvp->member->name}\t{$rsvp->response}".PHP_EOL;

                $this->insertRsvp($rsvp);
            }
        }
    }

    protected function insertRsvp($rsvp)
    {
        $sql = "INSERT INTO rsvps (memberId, eventId, response, guests, attendance_status, created, updated, data)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            response = VALUES(response),
            guests = VALUES(guests),
            attendance_status = VALUES(attendance_status),
            updated = VALUES(updated),
            data = VALUES(data)";

        $params = [
            $rsvp->member->id,
            $rsvp->event->id,
            $rsvp->response,
            $rsvp->guests,
            !empty($rsvp->attendance_status) ? $rsvp->attendance_status : null,
            date('Y-m-d H:i:s', ($rsvp->created / 1000)),
            date('Y-m-d H:i:s', ($rsvp->updated / 1000)),
            json_encode($rsvp)
        ];

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->rowCount();
    }

    protected function getRsvpsForEvent($event)
    {
        $response = $this->request(
            '/'.$this->config['groupUrl'].'/events/'.$event->id.'/rsvps',
            [
                'fields' => 'attendance_status,created,event,guests,member,response,updated',
                //'page' => $page,
                //'offset' => $offset,
            ]
        );

        return $response;
    }
}
