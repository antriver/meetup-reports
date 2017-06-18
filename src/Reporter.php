<?php

namespace Meetup;

use Carbon\Carbon;
use PDO;

class Reporter
{
    /**
     * @var Meetup
     */
    private $meetup;
    /**
     * @var PDO
     */
    private $db;

    /**
     * Reporter constructor.
     *
     * @param Meetup $meetup
     */
    public function __construct(Meetup $meetup, PDO $db)
    {
        $this->meetup = $meetup;
        $this->db = $db;
    }

    protected function getEventSelect()
    {
        return "e.*, 
                SUM(CASE WHEN r.response = 'yes' THEN 1 ELSE 0 END) AS yes,
                SUM(CASE WHEN r.response = 'no' THEN 1 ELSE 0 END) AS no,
                SUM(r.guests) AS guests,
                COUNT(r.id) AS rsvps,
                
                (CASE 
                    WHEN COUNT(r.id) > 1 THEN
                        SUM(CASE WHEN r.response = 'no' THEN 1 ELSE 0 END) / COUNT(r.id) * 100
                     ELSE 0 
                 END) AS noPercent";
    }

    /**
     * Returns the 100 most popular events.
     */
    public function getMostYesEvents()
    {
        $sql = "SELECT 
            {$this->getEventSelect()}           
            FROM events e
            JOIN rsvps r on r.eventId = e.id
            GROUP BY e.id
            ORDER BY yes DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    /**
     * Returns the 100 most popular events.
     */
    public function getMostNoEvents()
    {
        $sql = "SELECT 
                {$this->getEventSelect()}
            FROM events e
            JOIN rsvps r on r.eventId = e.id
            GROUP BY e.id
            ORDER BY noPercent DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    /**
     * Returns the 100 most popular events.
     */
    public function getLeastYesEvents()
    {
        $sql = "SELECT 
                {$this->getEventSelect()}
            FROM events e
            LEFT JOIN rsvps r on r.eventId = e.id
            WHERE status != 'cancelled'
            GROUP BY e.id
            ORDER BY yes ASC
            LIMIT 100";

        return $this->db->query($sql);
    }

    protected function getMemberSelect()
    {
        return "m.*, 
                SUM(CASE WHEN r.response = 'yes' THEN 1 ELSE 0 END) AS yes,
                SUM(CASE WHEN r.response = 'no' THEN 1 ELSE 0 END) AS no,
                SUM(CASE WHEN r.attendance_status = 'noshow' THEN 1 ELSE 0 END) AS noshows,
                COUNT(r.id) AS rsvps,
                
                (CASE 
                    WHEN COUNT(r.id) > 1 THEN
                        SUM(CASE WHEN r.response = 'yes' THEN 1 ELSE 0 END) / COUNT(r.id) * 100
                     ELSE 0 
                 END) AS yesPercent,
                 
                 (CASE 
                    WHEN COUNT(r.id) > 1 THEN
                        SUM(CASE WHEN r.response = 'no' THEN 1 ELSE 0 END) / COUNT(r.id) * 100
                     ELSE 0 
                 END) AS noPercent,
                 
                 (CASE 
                    WHEN COUNT(r.id) > 1 THEN
                        SUM(CASE WHEN r.attendance_status = 'noshow' THEN 1 ELSE 0 END) / COUNT(r.id) * 100
                     ELSE 0 
                 END) AS noshowPercent
                 ";
    }

    public function getAllMembers()
    {
        $sql = "SELECT 
            m.*       
            FROM members m
            GROUP BY m.id
            ORDER BY joined_group DESC";

        return $this->db->query($sql);
    }

    public function getMostYesMembers()
    {
        $sql = "SELECT 
            {$this->getMemberSelect()}           
            FROM members m
            JOIN rsvps r on r.memberId = m.id
            GROUP BY m.id
            ORDER BY yes DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    public function getRecentMostYesMembers()
    {
        $cutoff = (new Carbon("-3 MONTHS"))->toDateTimeString();

        $sql = "SELECT 
            {$this->getMemberSelect()}           
            FROM members m
            JOIN rsvps r on r.memberId = m.id
            WHERE r.created >= '{$cutoff}'
            GROUP BY m.id
            ORDER BY yes DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    public function getMostNoMembers()
    {
        $sql = "SELECT 
            {$this->getMemberSelect()}           
            FROM members m
            JOIN rsvps r on r.memberId = m.id
            GROUP BY m.id
            ORDER BY no DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    public function getMostNoShows()
    {
        $sql = "SELECT 
            {$this->getMemberSelect()}           
            FROM members m
            JOIN rsvps r on r.memberId = m.id
            GROUP BY m.id
            ORDER BY noshows DESC
            LIMIT 100";

        return $this->db->query($sql);
    }

    public function getFeeContributors()
    {
        $sql = "SELECT 
            m.*,
            paidAt,
            amount           
            FROM members m
            JOIN member_payments mp ON mp.memberId = m.id
            ORDER BY paidAt DESC";

        return $this->db->query($sql);
    }

    /**
     * Returns the number of yes rsvps there have been for events in the last 6 months.
     */
    public function getRecentTotalYesRsvps()
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-6 MONTHS'));

        $sql = "select count(*) as c from rsvps r join events e on e.id = r.`eventId` 
        where e.time >= ? and r.response = 'yes'";

        $statement = $this->db->prepare($sql);
        $statement->execute([$cutoff]);

        return $statement->fetchColumn();
    }

    /**
     * Returns the number of yes rsvps there have been for events in the last 6 months.
     */
    public function getMembersRecentYesRsvps()
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-6 MONTHS'));

        $sql = "select m.*, count(*) AS yesRsvps
        from rsvps r 
        join events e on e.id = r.`eventId` 
        join members m on m.id = r.`memberId`
        where e.time >= '{$cutoff}' and r.response = 'yes' 
        group by m.id
        order by yesRsvps desc
        limit 50";

        $results = $this->db->query($sql);

        return $results->fetchAll();
    }
}
