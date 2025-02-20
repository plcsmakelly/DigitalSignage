@extends('frame')

@section('js-footer')
<script src="https://unpkg.com/popper.js@1"></script>
<script src="https://unpkg.com/tippy.js@5"></script>
<script>
tippy('[data-tippy-content]');
function deleteSingle(id) {
swal({
  title: "Are you sure?",
  text: "The device will stop receiving content immediately!",
  icon: "warning",
  buttons: {
  cancel: {
    text: "Cancel",
    value: false,
    visible: true,
    className: "",
    closeModal: true,
  },
  confirm: {
    text: "Confirm",
    value: true,
    visible: true,
    className: "",
    closeModal: false
  }
},
  dangerMode: true,
  closeModal: false
})
.then((willDelete) => {
  if (willDelete) {
    window.location.href = "{{ url('devices/delete') }}/".concat(id);
  }
});
}
</script>
@stop

@section('content')
  <header class="bg-white shadow">
<div class="lg:flex lg:items-center lg:justify-between max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<div class="flex-1 min-w-0">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
      Devices
    </h2>
    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
      <div class="mt-2 flex items-center text-sm text-gray-500">
	A listing of all devices registered on the system.
      </div>
    </div>
  </div>
</div>
  </header>
  <main>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">


<div class="flex flex-col">
  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
      <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Name
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Zone
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50">
                <span class="sr-only">Edit</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
	    @foreach($devices as $device)
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="ml-1">
                    <div class="text-sm font-medium text-gray-900">
                      {{ $device->name }}
                      @if($device->hasBeenSeenRecently())
                      <span data-tippy-content="Device has recently been online and downloaded content." class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1.5em"fill="currentColor" stroke="currentColor" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>
                      </span>
                      @else
                      <span data-tippy-content="Device has been offline for {{ \Carbon\Carbon::parse($device->last_check)->diffForHumans(\Carbon\Carbon::now(), \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}" class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" fill="currentColor" stroke="currentColor" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M376.6 84.5c11.3-13.6 9.5-33.8-4.1-45.1s-33.8-9.5-45.1 4.1L192 206 56.6 43.5C45.3 29.9 25.1 28.1 11.5 39.4S-3.9 70.9 7.4 84.5L150.3 256 7.4 427.5c-11.3 13.6-9.5 33.8 4.1 45.1s33.8 9.5 45.1-4.1L192 306 327.4 468.5c11.3 13.6 31.5 15.4 45.1 4.1s15.4-31.5 4.1-45.1L233.7 256 376.6 84.5z"/></svg>  
                      </span>
                      @endif
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
		  @if ($device->zone_id > 0)
			{{ $device->getZone()->name }}
		  @else
			No zone assigned
		  @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="{{ url('devices/edit/'.$device->id) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">Reassign</a>
                <a href="javascript:deleteSingle({{ $device->id }})" class="mr-3 text-red-600 hover:text-red-900">Forget</a>
              </td>
            </tr>
	    @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
    </div>
  </main>
@stop
