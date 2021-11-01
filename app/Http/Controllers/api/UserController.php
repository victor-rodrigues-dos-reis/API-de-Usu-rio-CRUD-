<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
	/*
	██████╗ ██╗   ██╗██████╗ ██╗     ██╗ ██████╗
	██╔══██╗██║   ██║██╔══██╗██║     ██║██╔════╝
	██████╔╝██║   ██║██████╔╝██║     ██║██║     
	██╔═══╝ ██║   ██║██╔══██╗██║     ██║██║     
	██║     ╚██████╔╝██████╔╝███████╗██║╚██████╗
	╚═╝      ╚═════╝ ╚═════╝ ╚══════╝╚═╝ ╚═════╝
	*/
	/**
	 * Lista todos os usuários do banco de dados.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$userList = User::paginate(50);

		return $userList;
	}

	/**
	 * Cria um novo usuário no banco de dados
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{	
		$content 		= $request->getContent();
		$contentArray 	= $content ? json_decode($content, true) : [];
		
		// Verifica se os dados da requisição (user) foram passado corretamente
		$response = $this->validateUser($contentArray, true);

		// Verifica se foi retornado um resposta da função
		if ($response) {
			return $response;
		}

		// Cria o objeto de usuário com os dados passados pela requisição
		$user 			= new User();
		$user->name 	= $contentArray['name'];
		$user->password = bcrypt($contentArray['password']);
		$user->email	= $contentArray['email'];
		$user->save();

		return $user;
	}

	/**
	 * Listar um usuário em específico
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		// Verifica se o id foi passado corretamente
		$response = $this->validateUserId($id);

		// Verifica se foi retornado um resposta da função
		if ($response) {
			return $response;
		}

		return User::findOrFail($id);
	}

	/**
	 * Atualiza um usuário específico
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		/**************************
		***   VALIDAÇÃO DO ID   ***
		***************************/
		// Verifica se o id foi passado corretamente
		$response = $this->validateUserId($id);

		// Verifica se foi retornado um resposta da função
		if ($response) {
			return $response;
		}

		/**************************************
		***   VALIDAÇÃO DA REQUEST (USER)   ***
		***************************************/
		$content 		= $request->getContent();
		$contentArray 	= $content ? json_decode($content, true) : [];
		
		// Verifica se os dados da requisição (user) foram passado corretamente
		$response = $this->validateUser($contentArray, false);

		// Verifica se foi retornado um resposta da função
		if ($response) {
			return $response;
		}

		// Atualiza os dados do usuário de acordo as informações passadas na requisição
		$user = User::findOrFail($id);
		$user->update($request->all());

		return $user;
	}

	/**
	 * Deleta um usuário em específico
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		// Verifica se o id foi passado corretamente
		$response = $this->validateUserId($id);

		// Verifica se foi retornado um resposta da função
		if ($response) {
			return $response;
		}

		$user = User::findOrFail($id);
		$user->delete();
	}


	/*
	██████╗ ██████╗ ██╗██╗   ██╗ █████╗ ████████╗███████╗
	██╔══██╗██╔══██╗██║██║   ██║██╔══██╗╚══██╔══╝██╔════╝
	██████╔╝██████╔╝██║██║   ██║███████║   ██║   █████╗  
	██╔═══╝ ██╔══██╗██║╚██╗ ██╔╝██╔══██║   ██║   ██╔══╝  
	██║     ██║  ██║██║ ╚████╔╝ ██║  ██║   ██║   ███████╗
	╚═╝     ╚═╝  ╚═╝╚═╝  ╚═══╝  ╚═╝  ╚═╝   ╚═╝   ╚══════╝
	*/
	/**
	 * Valida o id de usuário 
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	private function validateUserId($id) {
		$rules = [
			'id' 		=> 'required|integer|exists:users,id',
		];

		$messages = [
			'required' 	=> "Está faltando o atributo ':attribute'.",
			'integer'	=> "O atributo ':attribute' deve ser do tipo numérico inteiro.",
			'exists'	=> "O :attribute informado não existe no banco de dados."
		];

		$arrayId	= ["id" => $id];

		// Cria a validação do id
		$validator 	= Validator::make($arrayId, $rules, $messages);

		// Verifica se algum campo falhou na verificação
		if ($validator->fails()) {
			$allErrors = $validator->errors()->all();
			$response = [
				"errors" => $allErrors
			];

			// Retorna qual erro ocorreu
			return response()->json($response, 400);
		}
	}

	/**
	 * Valida o usuário
	 *
	 * @param  array  $contentArray
	 * @param  boolean  $isRequired
	 * @return \Illuminate\Http\Response
	 */
	private function validateUser($contentArray, $isRequired) {
		$required 		= $isRequired ? "required|" : "";

		$rules = [
			'name' 		=> $required.'min:2|max:50',
			'email' 	=> $required.'email|unique:users,email|min:5|max:255',
			'password' 	=> $required.'min:6|max:32',
		];

		$messages = [
			'required' 	=> "Está faltando o atributo ':attribute'.",
			'min' 		=> "O atributo ':attribute' deve ter no mínimo :min caracteres.",
			'max' 		=> "O atributo ':attribute' deve ter no máximo :max caracteres.",
			'email' 	=> "O atributo ':attribute' informado não é válido.",
			'unique'	=> "O atributo ':attribute' informado já está sendo usado."
		];

		// Cria a validação do usuário
		$validator = Validator::make($contentArray, $rules, $messages);

		// Verifica se algum campo falhou na verificação
		if ($validator->fails()) {
			$allErrors = $validator->errors()->all();
			$response = [
				"errors" => $allErrors
			];

			// Retorna qual erro ocorreu
			return response()->json($response, 400);
		}
	}
}
