<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incomplete Survey Reminder</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            color: #ffffff;
            padding: 35px 25px 25px;
            text-align: center;
        }
        .header-icon {
            display: inline-block;
            width: 56px;
            height: 56px;
            line-height: 56px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            font-size: 28px;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.95;
        }
        .body {
            padding: 30px 25px;
        }
        .badge-pill {
            display: inline-block;
            background-color: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 15px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .content-text {
            font-size: 14px;
            color: #475569;
            margin-bottom: 20px;
        }
        .progress-card {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px solid #f59e0b;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .progress-title {
            font-size: 14px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 10px;
        }
        .progress-bar-bg {
            background-color: #e2e8f0;
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            margin: 10px auto;
            max-width: 90%;
        }
        .progress-bar-fill {
            background: linear-gradient(90deg, #f59e0b 0%, #10b981 100%);
            height: 100%;
            border-radius: 6px;
        }
        .progress-stat {
            font-size: 22px;
            font-weight: 800;
            color: #b45309;
            margin-top: 8px;
        }
        .details-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 25px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 7px 0;
            font-size: 13px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-label {
            color: #64748b;
            font-weight: 600;
            width: 38%;
        }
        .details-val {
            color: #0f172a;
            font-weight: 600;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 25px;
        }
        .btn {
            display: inline-block;
            padding: 14px 36px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            letter-spacing: 0.02em;
        }
        .note-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 20px;
        }
        .alt-link {
            font-size: 12px;
            color: #64748b;
            word-break: break-all;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 25px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">&#9201;</div>
            <h1>Your Survey Is Incomplete</h1>
            <p>State Skill Insight Assessment</p>
        </div>
        <div class="body">
            <span class="badge-pill">&#9888; Action Required: Resume Survey</span>
            <div class="greeting">Dear {{ $respondent->name }},</div>
            <div class="content-text">
                We noticed that you started the <strong>{{ $survey->title ?? 'State Skill Insight Survey' }}</strong>, but have not finished submitting all required responses yet.
            </div>

            <div class="progress-card">
                <div class="progress-title">Your Current Survey Progress</div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: {{ max(5, min(100, $percentage)) }}%;"></div>
                </div>
                <div class="progress-stat">{{ $percentage }}% Completed</div>
                <small style="color: #92400e; font-size: 12px;">All your previous answers are securely auto-saved!</small>
            </div>

            <div class="details-card">
                <table class="details-table">
                    <tr>
                        <td class="details-label">Survey Title:</td>
                        <td class="details-val">{{ $survey->title ?? 'State Skill Insight Survey' }}</td>
                    </tr>
                    @if($category)
                    <tr>
                        <td class="details-label">Category:</td>
                        <td class="details-val">{{ $category->name }} ({{ $category->code }})</td>
                    </tr>
                    @endif
                    @if($university)
                    <tr>
                        <td class="details-label">Institution:</td>
                        <td class="details-val">
                            {{ $university->name }}
                            @if($university->short_name) ({{ $university->short_name }})@endif
                        </td>
                    </tr>
                    @endif
                    @if($respondentSurvey->currentSection)
                    <tr>
                        <td class="details-label">Current Section:</td>
                        <td class="details-val">{{ $respondentSurvey->currentSection->title }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="btn-container">
                <a href="{{ $resumeUrl }}" class="btn" target="_blank">
                    Resume & Complete Survey &rarr;
                </a>
            </div>

            <div class="note-box">
                <strong>Why your response matters:</strong> Your completed feedback is essential for assessing practical skill readiness, institutional development, and curriculum modernization. It takes only a few more minutes to complete!
            </div>

            <div class="alt-link">
                <strong>Button not working?</strong> You can also copy and paste this link into your browser:<br>
                <a href="{{ $resumeUrl }}" style="color: #2563eb;">{{ $resumeUrl }}</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} State Skill Insight. All rights reserved.<br>
            This is an automated reminder. If you have already completed the survey recently, please disregard this email.
        </div>
    </div>
</body>
</html>
