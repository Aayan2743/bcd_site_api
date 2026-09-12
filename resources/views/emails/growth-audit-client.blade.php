<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Consultation Request Received</title>
</head>

<body style="font-family:Arial,sans-serif;background:#f5f5f5;padding:30px;">

<div style="
    max-width:650px;
    margin:auto;
    background:#ffffff;
    padding:30px;
    border-radius:10px;
">

    <h2>
        Thank you, {{ $audit->client_name }}!
    </h2>

    <p>
        We have received your free consultation request.
    </p>

    <p>
        Our team will review your requirements and get back to you shortly.
    </p>

    <hr>

    <h3>Your Request</h3>

    <p>
        <strong>Business Type:</strong>
        {{ $audit->business_type }}
    </p>

    <p>
        <strong>Primary Goal:</strong>
        {{ $audit->primary_goal }}
    </p>

    <p>
        <strong>Main Channel:</strong>
        {{ $audit->main_channel ?? 'Not specified' }}
    </p>

    <p>
        <strong>Biggest Challenge:</strong>
        {{ $audit->biggest_challenge }}
    </p>

    <p>
        <strong>Monthly Budget:</strong>
        {{ $audit->monthly_budget ?? 'Not specified' }}
    </p>

    <p>
        <strong>Timeline:</strong>
        {{ $audit->timeline ?? 'Not specified' }}
    </p>

    @if($audit->recommended_package)

        <hr>

        <h3>Recommended Start</h3>

        <p>
            {{ $audit->recommended_package }}
        </p>

    @endif

    <hr>

    <p>
        Thank you for choosing Growth Advisor.
    </p>

</div>

</body>
</html>