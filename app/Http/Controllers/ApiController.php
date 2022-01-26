<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Illuminate\Http\Response as Res;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

class ApiController extends Controller
{

    public function __construct() {
        $this->beforeFilter('auth', ['on' => 'post']);
    }

    protected $statusCode = Res::HTTP_OK;


    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    /**
     * @param $message
     * @return json response
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function respondCreated($message, $data=null){

        return $this->respond([

            'status' => 'success',
            'status_code' => Res::HTTP_CREATED,
            'message' => $message,
            'data' => $data

        ]);

    }

    /**
     * @param Paginator $paginate
     * @param $data
     * @return mixed
     */
    protected function respondWithPagination(Paginator $paginate, $data, $message){

        $data = array_merge($data, [
            'paginator' => [
                'total_count'  => $paginate->total(),
                'total_pages' => ceil($paginate->total() / $paginate->perPage()),
                'current_page' => $paginate->currentPage(),
                'limit' => $paginate->perPage(),
            ]
        ]);

        return $this->respond([

            'status' => 'success',
            'status_code' => Res::HTTP_OK,
            'message' => $message,
            'data' => $data

        ]);
    }


    public function respondNotFound($message = 'Not Found!'){

        return $this->respond([

            'status' => 'error',
            'status_code' => Res::HTTP_NOT_FOUND,
            'message' => $message,

        ]);

    }

    public function respondInternalError($message){

        return $this->respond([

            'status' => 'error',
            'status_code' => Res::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $message,

        ]);

    }

    public function respondValidationError($message, $errors){

        return $this->respond([

            'status' => 'error',
            'status_code' => Res::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $message,
            'data' => $errors

        ]);

    }

    public function respond($data, $headers = []){

        return Response::json($data, $this->getStatusCode(), $headers);

    }

    public function respondWithError($message){
        return $this->respond([

            'status' => 'error',
            'status_code' => Res::HTTP_UNAUTHORIZED,
            'message' => $message,

        ]);
    }


  

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
