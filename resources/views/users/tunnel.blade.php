<table class="table">
   <thead>
      <tr>
         <th>ID</th>
         <th>Server IPv4</th>
         <th>Server IPv6</th>
         <th>Client IPv4</th>
         <th>Client IPv6</th>
         <!-- <th>Routed IPv6</th> -->
         <!-- <th>rDNS Delegations</th> -->
         <th>Action</th>
      </tr>
   </thead>
   <tbody>
      @foreach($tunnels as $tunnel)
      <tr>
      <th>{{$tunnel->id}}</th>
      <th>{{$tunnel->server_ipv4}}</th>
      <th>{{$tunnel->server_ipv6}}</th>
      <th>{{$tunnel->client_ipv4}}</th>
      <th>{{$tunnel->client_ipv6}}</th>
      <th>
         <form action="{{ route('tunnel.edit') }}" method="POST">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{$tunnel->id}}">
            <button class="btn-info" type="submit" name="button">操作</button>
         </form>
      </th>
      </tr>
      @endforeach
   </tbody>
</table>