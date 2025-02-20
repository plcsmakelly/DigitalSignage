@extends('frame')

@section('js-footer')
<script>
function deleteSingle(id) {
swal({
  title: "Are you sure?",
  text: "The zone will be unrecoverable!",
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
    window.location.href = "{{ url('zones/delete') }}/".concat(id);
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
      Zones
    </h2>
    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
      <div class="mt-2 flex items-center text-sm text-gray-500">
	A listing of all display zones you have access to.
      </div>
    </div>
  </div>
@if(Auth::user()->isAdmin())
  <div class="mt-5 flex lg:mt-0 lg:ml-4">
    <span class="hidden sm:block">
      <a href="{{ url('zones/new') }}" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
        <svg xmlns="http://www.w3.org/2000/svg" height="1.2em" class="mr-2" stoke="currentColor" fill="currentColor" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"/></svg>
	      New Zone
      </a>
    </span>
  </div>
@endif
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
                Devices
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Items
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50">
                <span class="sr-only">Edit</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
	    @foreach($zones as $zone)
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="ml-1">
                    <div class="text-sm font-medium text-gray-900">
                      {{ $zone->name }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">@if($zone->type == "root_zone") {{ $zone->getDeviceCount() }} @else - @endif</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ $zone->getContentCount() }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
		@if($zone->enabled)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  Active
                </span>
		@else
		<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                  Disabled
                </span>
		@endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @if($zone->type == "root_zone") Root Zone @else Sub Zone @endif
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <a href="{{ url('zones/permissions/list/'.$zone->id) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">Permissions</a>
	        <a href="{{ url('zones/content/list/'.$zone->id) }}" class="mr-3 text-indigo-600 hover:text-indigo-900">Edit Content</a>
		@if(Auth::user()->isAdmin())
		<a href="javascript:deleteSingle({{ $zone->id }})" class="mr-3 text-red-600 hover:text-red-900">Delete</a>
		@endif
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
