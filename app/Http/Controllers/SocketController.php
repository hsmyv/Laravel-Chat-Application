<?php

namespace App\Http\Controllers;

use App\Models\Chat_request;
use App\Models\User;
use Illuminate\Http\Request;

use Ratchet\MessageComponentInterface;

use Ratchet\ConnectionInterface;



class SocketController extends Controller implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SpLObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $querystring = $conn->httpRequest->getUri()->getQuery();

        parse_str($querystring, $queryarray);

        if(isset($queryarray['token'])){
            User::where('token', $queryarray['token'])->update(['connection_id' => $conn->resourceId]);
        }

    }

    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $data = json_decode($msg);

        if(isset($data->type))
        {
            if($data->type == 'request_load_unconnected_user')
            {
                $user_data = User::select('id', 'name', 'user_status', 'user_image')
                                    ->where('id', '!=', $data->from_user_id)
                                    ->orderBy('name', 'ASC')
                                    ->get();

                $sub_data = array();

                foreach ($user_data as $row) {
                    $sub_data[] = array(
                        'name' => $row['name'],
                        'id'   => $row['id'],
                        'status'=> $row['user_status'],
                        'user_image' => $row['user_image']
                    );
                }

                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                $send_data['data'] = $sub_data;

                $send_data['response_load_unconnected_user'] = true;


                foreach ($this->clients as $client) {
                    if($client->resourceId == $sender_connection_id[0]->connection_id)
                    {
                        $client->send(json_encode($send_data));
                    }
                }
            }
            if ($data->type == 'request_search_user')
            {
                $user_data = User::select('id', 'name', 'user_status', 'user_image')
                    ->where('id', '!=', $data->from_user_id)
                    ->where('name', 'like', '%' . $data->search_query . '%')
                    ->orderBy('name', 'ASC')
                    ->get();

                $sub_data = array();

                foreach ($user_data as $row) {
                    $sub_data[] = array(
                        'name' => $row['name'],
                        'id'   => $row['id'],
                        'status' => $row['user_status'],
                        'user_image' => $row['user_image']
                    );
                }

                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                $send_data['data'] = $sub_data;

                $send_data['response_search_user'] = true;


                foreach ($this->clients as $client) {
                    if ($client->resourceId == $sender_connection_id[0]->connection_id) {
                        $client->send(json_encode($send_data));
                    }
                }
            }
            if ($data->type == 'request_chat_user') {
                $chat_request = new Chat_request;

                $chat_request->from_user_id = $data->from_user_id;

                $chat_request->to_user_id = $data->to_user_id;

                $chat_request->status = 'Pending';

                $chat_request->save();

                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                $receiver_connection_id = User::select('connection_id')->where('id', $data->to_user_id)->get();

                foreach ($this->clients as $client) {
                    if ($client->resourceId == $sender_connection_id[0]->connection_id) {
                        $send_data['response_from_user_chat_request'] = true;
                        $client->send(json_encode($send_data));
                    }
                }
            }
        }

    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $querystring = $conn->httpRequest->getUri()->getQuery();

        parse_str($querystring, $queryarray);

        if (isset($queryarray['token'])) {
            User::where('token', $queryarray['token'])->update(['connection_id' => 0]);
        }

    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()} \n";

        $conn->close();
    }
}
