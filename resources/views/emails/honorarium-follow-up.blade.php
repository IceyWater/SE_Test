<p>Hello {{ $recipientRole }},</p>

<p>{{ $senderName }} followed up on the honorarium request for:</p>

<ul>
    <li>Student: {{ trim(($defenseRequest->first_name ?? '').' '.($defenseRequest->last_name ?? '')) }}</li>
    <li>Program: {{ $defenseRequest->program }}</li>
    <li>Defense: {{ $defenseRequest->thesis_title }}</li>
    <li>Request: #{{ $defenseRequest->id }}</li>
</ul>

<p>Follow-up note:</p>
<p>{{ $notes }}</p>

<p><a href="{{ $trackingUrl }}">Open honorarium tracking</a></p>
