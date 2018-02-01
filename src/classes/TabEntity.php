<?php

class TabEntity 
{
    protected $url;
    protected $date;
    protected $host;
    protected $timestamp;
    protected $icon;
    protected $title;

    public function isYT($url) {
        return strpos($url, 'youtube.com/watch?') !== false;
    }

    public function __construct(array $data, $filter_url) {
        $url = $data['url'];

        if($filter_url && $this->isYT($url)) {
            // Filters out the '&t' parameter from the URL.
            // Only invoked when we add a YouTube tab to the database.
            // https://regex101.com/r/TdJn4m/1/tests
            $this->url = preg_replace("/((?:&|\?)t=[^&]+)/", "", $url);
        }
        else {
            $this->url = $url;
        }

        $this->date = $data['date'];
        $this->host = $data['host'];
        $this->timestamp = $data['timestamp'];
        $this->icon = $data['icon'];
        $this->title = $data['title'];
    }

    public function formatSeconds($seconds)
    {
        // Converts our time in seconds given by the DOM
        // to time readable by the YouTube Player.
        // TODO: Add support for other player parameters in a global database

        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        $format = '%uh%02um%02us';
        $time = sprintf($format, $hours, $minutes, $seconds);
        return rtrim($time, '0');
    }

    public function getDate() {
        return $this->date;
    }

    public function getHost() {
        return $this->host;
    }

    public function getTimestamp() {
        return $this->formatSeconds($this->timestamp);
    }

    public function getTimestampClean() {
        return $this->timestamp;
    }
    
    public function getIcon() {
        return $this->icon;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getURL() {
        // Only add the timestamp if the URL is YouTube
        if($this->isYT($this->url)) {
            $url = $this->url . "&t=" . $this->getTimestamp();
        }
        else {
            $url = $this->url;
        }
        return $url;
    }

    public function getURLClean() {
        return $this->url;
    }
}

?>