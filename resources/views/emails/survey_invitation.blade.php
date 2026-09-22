<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Invitation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e1e8ed;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .body {
            padding: 30px 25px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e3c72;
            margin-bottom: 15px;
        }
        .content-text {
            font-size: 14px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 25px;
        }
        .survey-box {
            background-color: #f8fafc;
            border-left: 4px solid #1e3c72;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .survey-box h3 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #1e3c72;
        }
        .survey-box p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background-color: #1e3c72;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            box-shadow: 0 4px 10px rgba(30, 60, 114, 0.3);
        }
        .alt-link {
            font-size: 12px;
            color: #888888;
            word-break: break-all;
            margin-top: 25px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 4px;
        }
        .footer {
            background-color: #f4f6f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888888;
            border-top: 1px solid #e1e8ed;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>State Skill Insight</h1>
            <p>Survey & Assessment Portal</p>
        </div>
        <div class="body">
            <div class="greeting">Hello {{ $invitation->name }},</div>
            <div class="content-text">
                You have been invited to participate in an official survey. Your valuable feedback and insights will contribute significantly to state skill analysis and institutional evaluations.
            </div>

            @if($invitation->survey)
            <div class="survey-box">
                <h3>{{ $invitation->survey->title }}</h3>
                @if($invitation->survey->description)
                <p>{{ Str::limit($invitation->survey->description, 120) }}</p>
                @endif
            </div>
            @endif

            <div class="btn-container">
                <a href="{{ $surveyUrl }}" class="btn" target="_blank">Take Survey Now</a>
            </div>

            <div class="content-text">
                This link is unique to you. Please do not forward or share this link with anyone else.
            </div>

            <div class="alt-link">
                <strong>Button not working?</strong> Copy and paste this URL into your browser:<br>
                <a href="{{ $surveyUrl }}" style="color: #1e3c72;">{{ $surveyUrl }}</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} State Skill Insight. All rights reserved.<br>
            This is an automated email. Please do not reply directly to this message.
        </div>
    </div>
</body>
</html>
