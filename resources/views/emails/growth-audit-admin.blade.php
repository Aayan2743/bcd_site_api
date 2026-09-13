<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Growth Audit Request</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:30px;">

<div style="
    max-width:650px;
    margin:auto;
    background:#ffffff;
    padding:30px;
    border-radius:10px;
">

    <h2 style="margin-top:0;">
        New Growth Audit Request
    </h2>

    <p>
        You have received a new free consultation request.
    </p>

    <hr>

    <h3>Client Details</h3>

    <p>
        <strong>Name:</strong>
        {{ $audit->client_name }}
    </p>

    <p>
        <strong>Phone:</strong>
        {{ $audit->phone }}
    </p>

    <p>
        <strong>Email:</strong>
        {{ $audit->email }}
    </p>

    <p>
        <strong>Company:</strong>
        {{ $audit->company_name }}
    </p>

    <hr>

    <h3>Growth Audit Selections</h3>

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

        <h3>Recommended Package</h3>

        <p>
            {{ $audit->recommended_package }}
        </p>

    @endif

    <hr>

    <p style="color:#777;font-size:13px;">
        This enquiry was submitted through the Growth Advisor website.
    </p>

</div>

</body>
</html>