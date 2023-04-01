@component("mail::message")

# Получено новое обращение с agroprice.kz

## Тема письма: {{ $feedback->subject }}

## E-mail: {{ $feedback->email }}

## Текст письма: {{ $feedback->message }}

@endcomponent