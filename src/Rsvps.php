<?php

namespace Meetup;

class Rsvps extends AbstractDataSource
{
    public function update()
    {
        $events = $this->meetup->events->all();

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

    protected function insertRsvp($data)
    {
        if (empty($data->memberId) || empty($data->eventId) || empty($data->response)) {
            return false;
        }

        $keys = [
            'memberId',
            'eventId',
            'response',
            'guests',
            'attendance_status',
            'created',
            'updated',
        ];

        $dates = [
            'created',
            'updated',
        ];

        $data->memberId = $data->member->id;
        $data->eventId = $data->event->id;

        $this->insert('rsvps', $keys, $dates, $data);
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
