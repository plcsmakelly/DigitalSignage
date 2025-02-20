Name,URL,Duration,UpdateInterval,StartOn,EndBy,Cache
<?php $count = 1; ?>
@foreach($urls as $url)
Media{{ $count }},{{ $url }},0:00:08,1:00:00,1/1/18 0:00,12/31/30 23:59,yes
<?php $count++; ?>
@endforeach
