@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Licenciaturas')
<img src="https://www.facebook.com/photo/?fbid=170055858866096&set=a.170055855532763" class="logo" alt="Moctezuma">

@else
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@endif
</a>
</td>
</tr>
