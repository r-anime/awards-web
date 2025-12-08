<div>
    <!-- Nothing worth having comes easy. - Theodore Roosevelt -->
    Hello results index! {{ $yearlist->toJson() }}
        <br>
    @foreach ($yearlist as $year)
        {{ $year['year'] }}
        <br>
    @endforeach
    <br>
    <br>
    @php
        var_dump($yearlist);
    @endphp
</div>
