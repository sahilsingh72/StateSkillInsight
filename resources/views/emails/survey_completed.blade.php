<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Completed Successfully</title>
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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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
            background: rgba(255, 255, 255, 0.2);
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
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
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
        .details-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 25px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 8px 0;
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
        .progress-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .progress-box h3 {
            margin: 0;
            font-size: 18px;
            color: #15803d;
            font-weight: 700;
        }
        .progress-box p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #166534;
        }
        .thankyou-note {
            background-color: #f1f5f9;
            border-left: 4px solid #059669;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #334155;
            margin-bottom: 20px;
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
            <div class="header-icon">&#10004;</div>
            <h1>Survey Submitted Successfully</h1>
            <p>State Skill Insight Assessment</p>
        </div>
        <div class="body">
            <span class="badge-pill">&#10003; 100% Completed</span>
            <div class="greeting">Dear {{ $respondent->name }},</div>
            <div class="content-text">
                Thank you for participating in the <strong>{{ $survey->title ?? 'State Skill Insight Survey' }}</strong>. Your responses have been successfully recorded and processed.
            </div>

            <div class="progress-box">
                <h3>100% Survey Completion</h3>
                <p>All mandatory questions and categories answered successfully</p>
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
                            @if($university->parent)
                                <br><small style="color: #64748b; font-weight: normal;">Affiliated to: {{ $university->parent->name }}</small>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($respondent->programme)
                    <tr>
                        <td class="details-label">Programme & Dept:</td>
                        <td class="details-val">{{ $respondent->programme }} @if($respondent->department) — {{ $respondent->department }}@endif</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="details-label">Submitted On:</td>
                        <td class="details-val">{{ $respondentSurvey->completed_at ? $respondentSurvey->completed_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Reference ID:</td>
                        <td class="details-val" style="font-family: monospace; font-size: 12px; color: #059669;">{{ strtoupper(substr($respondent->token, 0, 16)) }}</td>
                    </tr>
                </table>
            </div>

            <div class="thankyou-note">
                <strong>Impact of your participation:</strong> Your insights will play a key role in skill gap identification, curriculum enhancements, and policy development for higher education and employability.
            </div>

            <div class="content-text" style="font-size: 13px; color: #64748b; margin-bottom: 0;">
                If you have any questions or require further assistance regarding this survey, please feel free to reach out to the institutional survey coordinators.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} State Skill Insight. All rights reserved.<br>
            This is an automated confirmation email. Please do not reply directly to this message.
        </div>
    </div>
</body>
</html>
