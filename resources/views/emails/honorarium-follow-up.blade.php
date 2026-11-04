<<<<<<< Updated upstream
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
=======
<!doctype html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#222;line-height:1.6">
    <h1 style="font-size:22px">Honorarium follow-up</h1>
    <p>A follow-up was requested for the defense honorarium of {{ $studentName }}.</p>
    <p><strong>Program:</strong> {{ $program }}</p>
    <p><strong>Defense:</strong> {{ $defenseType }}</p>
    <p><strong>Request:</strong> #{{ $requestId }}</p>
    <p><strong>Assigned office:</strong> {{ $recipientRole }}</p>
    <p><strong>Note:</strong> {{ $notes }}</p>
    <p><a href="{{ rtrim(config('app.url'), '/') }}/honorarium-workspace">Open Honorarium Tracking</a></p>
</body>
</html>
>>>>>>> Stashed changes
