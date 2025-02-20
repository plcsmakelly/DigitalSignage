@extends('frame')

@section('content')
  <header class="bg-white shadow">
<div class="lg:flex lg:items-center lg:justify-between max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
<div class="flex-1 min-w-0">
    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
      Zone Permissions: {{ $zone->name }}
    </h2>
    <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
      <div class="mt-2 flex items-center text-sm text-gray-500">
	You can add or remove additional editors to this zone on this page.
      </div>
    </div>
  </div>
</div>
  </header>
  <main>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

<div>
  <div class="md:grid md:grid-cols-3 md:gap-6">
    <div class="md:col-span-1">
      <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Permissions</h3>
        <p class="mt-1 text-sm text-gray-600">
          This is a list of users that are able to view and modify this zone.
        </p>
      </div>
    </div>
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow sm:rounded-md sm:overflow-hidden">
          <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
	  <div>
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
                Role
              </th>
              <th scope="col" class="px-6 py-3 bg-gray-50">
                <span class="sr-only">Remove</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
	    @foreach($zone->getPermissions() as $permission)
            <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ $permission->getUser()->username }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ $permission->getUser()->email }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">Editor</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                @if($permission->user_id != Auth::user()->id) <a href="{{ url('zones/permissions/remove/'.$permission->id) }}" class="text-red-600 hover:text-red-900">Delete</a> @else <span class="text-sm text-gray-500">Cannot delete yourself</span> @endif
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
<div>
<form action="{{ url('zones/permissions/add/'.$zone->id) }}" method="post">
{!! csrf_field() !!}
<input type="text" name="email" class="form-input block flex-1 w-full" placeholder="Type an email to add it and press enter..." autofocus>
</form>
</div>
        </div>
    </div>
  </div>
</div>
    </div>
  </main>
@stop
