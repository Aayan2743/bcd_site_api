<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application Received</title>
</head>
<body>
    <h2>Application Received</h2>

    <p>Dear {{ $application->full_name }},</p>

    <p>Thank you for applying for the position of <strong>{{ $application->job->job_title }}</strong>.</p>

    <p>We have successfully received your application and resume.</p>

    <p>Our team will review your application and contact you if your profile is shortlisted.</p>

    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>