<?php

namespace Meetup;

class Rsvps extends AbstractDataSource
{
    public function update()
    {
        $events = $this->meetup->events->all();

        foreach ($events as $event) {
            $rsvps = $this->getRsvpsForEvent($event);

            if (!$rsvps) {
                continue;
            }

            foreach ($rsvps as $rsvp) {
                echo "{$rsvp->event->id}\t{$rsvp->event->name}\t{$rsvp->member->id}";
                echo "\t{$rsvp->member->name}\t{$rsvp->response}".PHP_EOL;

                $this->insertRsvp($rsvp);
            }
        }
    }

    protected function insertRsvp($data)
    {
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
            'updated'
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
