import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { RouterConfigLoader } from '@angular/router/src/router_config_loader';
import { UserService } from '../services/user.service';

@Component({
    selector: 'login',
    templateUrl: '../views/login.html',
    providers: [UserService]
})

export class LoginComponent implements OnInit{
    public title: string;
    public user;
    public identity;
    public token;

    constructor(
        private _route: ActivatedRoute,
        private _router: Router,
        private _userService: UserService
    ){
      this.title = 'Identifícate';
      this.user = {
        /*Propiedades, declaradas vacías, porque queremos que se informen
        * con los datos obtenidos del formulario*/
        "email": "",
        "password": "",
        "getHash": "true" //para que nos devuelva los datos como tal y no el token (con false)
      };
    }

    ngOnInit(){
        console.log('El componente login.component.ts ha sido cargado!!');
        console.log(JSON.parse(localStorage.getItem('identity')));
        console.log(JSON.parse(localStorage.getItem('token')));
    }

    onSubmit(){
        console.log(this.user);

        //Hacemos una llamada al Servicio
        this._userService.signup(this.user).subscribe(
            response => {
                this.identity = response; //guardamos la respuesta que es el objeto de usuario loguedo

                if(this.identity.length <= 1){
                    console.log('Error en el servidor'); //Esto es cuando no nos devuelve bien el API
                }{
                    if(!this.identity.status){ //el status sólo se genera si se produce un error.
                      //setea en el localstorage la identidad del usuario, setea una variable nueva
                      // en el local storage que se llame identity, y le mete los datos del usuario
                      //logueado, que está en un objeto que se llama también identity
                     localStorage.setItem('identity', JSON.stringify(this.identity));

                     //GET TOKEN conseguimos el token
                     this.user.getHash = null;
                     this._userService.signup(this.user).subscribe(
                        response => {
                            this.token = response; //guardamos la respuesta que es el objeto de usuario loguedo
                            if(this.identity.length <= 1){
                                console.log('Error en el servidor'); //Esto es cuando no nos devuelve bien el API
                            }{
                                if(!this.identity.status){ //el status sólo se genera si se produce un error.
                                  //setea en el localstorage la identidad del usuario, setea una variable nueva
                                  // en el local storage que se llame identity, y le mete los datos del usuario
                                  //logueado, que está en un objeto que se llama también identity
                                 localStorage.setItem('token', JSON.stringify(this.token));
                                }
                            }
                        },
                        error => {
                            console.log(<any>error);
                        }
            
                    );





                     //
                    }
                }
            },
            error => {
                console.log(<any>error);
            }

        );
    }
}


 /*
    onSubmit(){
        console.log(this.user);

        this._userService.signup(this.user).subscribe(
            response => {
              this.identity = response; //guardamos la respuesta que es el objeto de usuario loguedo
            
              if(this.identity.length <= 1){
                  console.log('Error en el servidor'); //Esto es cuando no nos devuelve bien el API
              }{
                //El status se devuelve sólo si se produce un error  
                if(!this.identity.status){ 
                 //setea en el localstorage la identidad del usuario, setea una variable nueva
                 // en el local storage que se llame identity, y le mete los datos del usuario
                 //logueado, que está en un objeto que se llama también identity
                 localStorage.setItem('identity', JSON.stringify(this.identity));

                 //Get token
                 this.user.getHash = null; //para que retorne el token
                 this._userService.signup(this.user).subscribe(
                    response => {
                      this.token = response;
        
                      if(this.identity.length <= 1){
                          console.log('Error en el servidor');
                      }{
                        //El status se devuelve sólo si se produce un error  
                        if(!this.identity.status){
                         //setea en el localstorage la identidad del usuario, setea una variable nueva
                         // en el local storage que se llame identity, y le mete los datos del usuario
                         //logueado, que está en un objeto que se llama también identity
                         localStorage.setItem('token', JSON.stringify(this.token));
                      }
                    }
                    error => {
                        console.log(<any>error);
                    }
              } // response =>
            }// if(!this.identity.status){ 
         }
        },
        error => {
                console.log(<any>error);
        }       
    );
    }
    */
