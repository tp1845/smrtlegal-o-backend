@extends('emails.layout')

@section('content')
<div style="max-width: 400px; margin: 0 auto;">
    <br/><br/>
    <p style="color: #737373; text-align: center;">
        {{ $company_name }} has invited you to collaborate on the {{ $project_name }}

        <ul>
            <li>
                <strong>Document Type</strong>: {{ $document_type }}
            </li>
            <li>
                <strong>Proposed Due Date<strong>: {{ $due_date }}
            </li>
        </ul>
        
        

        <a href="#"
            style="background-color: #F0F6FF; white-space: nowrap; font-weight: bold; text-decoration: none; display: inline-block; cursor: pointer; color: #222; margin-top: 32px; border: 1px solid #737373; border-radius: 4px; min-with: 358px; padding: 10px 150px;">
            Join Project
        </a>
    </p>
</div>
@endsection
