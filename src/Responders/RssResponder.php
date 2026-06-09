<?php

declare(strict_types=1);

class RssResponder
{
    public function send(array $accidents): void
    {
        ob_clean();
        header('Content-Type: text/xml; charset=utf-8');

        $baseUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $buildDate = date(DATE_RFC822);

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<rss version=\"2.0\">\n";
        $xml .= "  <channel>\n";
        $xml .= "    <title>AVis Recent Accidents Feed</title>\n";
        $xml .= "    <link>" . htmlspecialchars($baseUrl . '/rss') . "</link>\n";
        $xml .= "    <description>The 100 most recent accident reports from the AVis platform.</description>\n";
        $xml .= "    <lastBuildDate>{$buildDate}</lastBuildDate>\n";

        foreach ($accidents as $acc) {
            $id = htmlspecialchars((string)($acc['id'] ?? ''));
            $severity = htmlspecialchars((string)($acc['severity'] ?? 'Unknown'));
            $city = htmlspecialchars((string)($acc['city'] ?? 'Unknown'));
            $state = htmlspecialchars((string)($acc['state'] ?? 'Unknown'));
            $dateTime = $acc['date_time'] ?? date('Y-m-d H:i:s');
            
            $title = "Severity {$severity} Accident in {$city}, {$state}";
            $description = "An accident with severity {$severity} was reported on {$dateTime} in {$city}, {$state}.";
            $pubDate = date(DATE_RFC822, strtotime($dateTime));
            
            $xml .= "    <item>\n";
            $xml .= "      <title>{$title}</title>\n";
            $xml .= "      <link>" . htmlspecialchars($baseUrl . '/?accident=' . urlencode((string)$acc['id'])) . "</link>\n";
            $xml .= "      <description>{$description}</description>\n";
            $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
            $xml .= "      <guid isPermaLink=\"false\">{$id}</guid>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= "</rss>\n";

        echo $xml;
        exit;
    }
}
