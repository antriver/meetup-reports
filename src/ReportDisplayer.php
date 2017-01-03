<?php

namespace Meetup;

use Carbon\Carbon;

class ReportDisplayer
{
    /**
     * @var Meetup
     */
    private $meetup;

    /**
     * ReportDisplayer constructor.
     *
     * @param Meetup $meetup
     */
    public function __construct(Meetup $meetup)
    {
        $this->meetup = $meetup;
    }

    public function showEvents($events)
    {
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Event</th>
                <th>Date</th>
                <th>Yes RSVPs</th>
                <th>Guests</th>
                <th>No RSVPs</th>
                <th>No %</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($events as $event) {
                echo "<tr>
                    <td><a href='{$this->meetup->eventUrl($event)}' target='_blank'>{$event->name}</a>";
                if ($event->status === 'cancelled') {
                    echo ' <span class="label label-danger label-sm">Cancelled</span>';
                }
                echo "</td>
                    <td>".(new Carbon($event->time))->toDayDateTimeString()."</td>
                    <td>".number_format($event->yes)."</td>
                    <td>".number_format($event->guests)."</td>
                    <td>".number_format($event->no)."</td>
                    <td>".number_format($event->noPercent)."%</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
    }

    public function showMembers($members)
    {
        ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Joined</th>
                <th>Total RSVPs</th>
                <th>Yes RSVPs</th>
                <th>No RSVPs</th>
                <th>No-Shows</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($members as $member) {
                echo "<tr>
                    <td><a href='{$this->meetup->memberUrl($member)}' target='_blank'>{$member->name}</a></td>
                    <td>".(new Carbon($member->joined))->toDayDateTimeString()."</td>
                    <td>".number_format($member->rsvps)."</td>
                    <td>".number_format($member->yes)."</td>
                    <td>".number_format($member->no)."</td>
                    <td>".number_format($member->noshows)."</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
        <?php
    }
}
