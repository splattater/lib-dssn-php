<?php
/**
 * Atom Feed of activities
 *
 * @author  {@link http://sebastian.tramp.name Sebastian Tramp}
 * @license http://sam.zoy.org/wtfpl/  Do What The Fuck You Want To Public License (WTFPL)
 */
class DSSN_Activity_Feed
{
    /*
     * feed->title
     */
    private $title = null;

    /*
     * feed->updated
     */
    private $updated = null;

    /*
     * feed->link rel=self
     */
    private $linkSelf = null;

    /*
     * feed->link rel=hub
     */
    private $linkHub = null;

    /*
     * feed->link rel=html
     */
    private $linkHtml = null;

    /*
     * The activities of the feed (FIFO)
     */
    private $activities = array();


    /*
     * send the feed to the air ...
     */
    public function send()
    {
        header('Content-type: application/atom+xml');
        echo $this->toXml();
    }

    /*
     * returns an activity feed XML string
     */
    public function toXml()
    {
        return $this->toDomDocument()->saveXML();
    }

    /*
     * return the activity feed as a DOMDocument object
     */
    public function toDomDocument()
    {
        $dom   = new DOMDocument('1.0', 'UTF-8');
        $feed  = $dom->createElementNS('http://www.w3.org/2005/Atom','feed');

        // feed->title
        $title = $dom->createElement('title', $this->getTitle());
        $feed->appendChild($title);

        // feed->updated
        $updated = $dom->createElement('updated', date('c', time()));
        $feed->appendChild($updated);

        // feed->link@self
        $linkSelf = $dom->createElement('link');
        $linkSelf->setAttribute('rel', 'self');
        $linkSelf->setAttribute('type', 'application/xml+atom');
        $linkSelf->setAttribute('href', $this->getLinkSelf());
        $feed->appendChild($linkSelf);

        // feed->link@html
        $linkHtml = $dom->createElement('link');
        $linkHtml->setAttribute('type', 'text/html');
        $linkHtml->setAttribute('href', $this->getLinkHtml());
        $feed->appendChild($linkHtml);

        // feed->link@hub
        if (isset($this->linkHub)) {
            $linkHub = $dom->createElement('link');
            $linkHub->setAttribute('rel', 'hub');
            $linkHub->setAttribute('href', $this->getLinkHub());
            $feed->appendChild($linkHub);
        }

        // feed->entries
        foreach ($this->getActivities() as $key => $activity) {
            $entry = $activity->toAtomEntry();
            $feed->appendChild($dom->importNode($entry, true));
        }

        $dom->appendChild($feed);
        return $dom;
    }

    /*
     * add an activity to the feed
     */
    public function addActivity(DSSN_Activity $activity)
    {
        $this->activities[] = $activity;
    }

    /*
     * returns an array of activities
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Get title.
     *
     * @return title.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param title the value to set.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get updated.
     *
     * @return updated.
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set updated.
     *
     * @param updated the value to set.
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get linkSelf.
     *
     * @return linkSelf.
     */
    public function getLinkSelf()
    {
        return $this->linkSelf;
    }

    /**
     * Set linkSelf.
     *
     * @param linkSelf the value to set.
     */
    public function setLinkSelf($linkSelf)
    {
        $this->linkSelf = $linkSelf;
    }

    /**
     * Get linkHtml.
     *
     * @return linkHtml.
     */
    public function getLinkHtml()
    {
        return $this->linkHtml;
    }

    /**
     * Set linkHtml.
     *
     * @param linkHtml the value to set.
     */
    public function setLinkHtml($linkHtml)
    {
        $this->linkHtml = $linkHtml;
    }

    /**
     * Get linkHub.
     *
     * @return linkHub.
     */
    function getLinkHub()
    {
        return $this->linkHub;
    }

    /**
     * Set linkHub.
     *
     * @param linkHub the value to set.
     */
    function setLinkHub($linkHub)
    {
        $this->linkHub = $linkHub;
    }
}
