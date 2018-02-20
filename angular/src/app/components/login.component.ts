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
        "gethash": "false"

      };      
    }

    ngOnInit(){
        console.log('El componente login.component.ts ha sido cargado!!');
    }

    onSubmit(){
        console.log(this.user);
        alert(this._userService.signup());
    }
}