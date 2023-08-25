@extends('emails.layout')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <p style="color: #737373;  text-align: center;">
        You have [#Tasks] due on {{ $due_date }}
        <ul>
            <li>
                <strong>Team</strong>: {{ $team }}
            </li>
            <li>
                <strong>Project Name</strong>: {{ $project_name }}
            </li>
            <li>
                <strong>Document Type</<strong>: {{ $document_type }}
            </li>
            <li>
                <strong>Due Date</strong>: {{ $due_date }}
            </li>
        </ul> 
    </p>

    <a href="#"
        style="background-color: #F0F6FF; white-space: nowrap; font-weight: bold; text-decoration: none; display: inline-block; cursor: pointer; color: #222; margin-top: 32px; border: 1px solid #737373; border-radius: 4px; min-with: 358px; padding: 10px 150px;">
        Complete Tasks
    </a>
</div>
@endsection
