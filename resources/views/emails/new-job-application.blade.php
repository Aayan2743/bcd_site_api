<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Job Application</title>
</head>
<body>
    <h2>New Job Application</h2>

    <p><strong>Job:</strong> {{ $application->job->job_title }}</p>

    <p><strong>Name:</strong> {{ $application->full_name }}</p>

    <p><strong>Email:</strong> {{ $application->email }}</p>

    <p><strong>Phone:</strong> {{ $application->phone }}</p>

    <p><strong>Experience:</strong> {{ $application->experience }}</p>

    <p><strong>Resume:</strong> Attached with this email.</p>

    <p><strong>Applied On:</strong> {{ $application->created_at }}</p>
</body>
</html>