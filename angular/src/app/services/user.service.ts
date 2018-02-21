import { Injectable } from "@angular/core";
import { Http, Response, Headers } from '@angular/http';
import "rxjs/add/operator/map"; //para capturar cada una de las peticiones ajax
import { Observable } from "rxjs/Observable";
import {GLOBAL} from "./global";

@Injectable()
export class UserService{

  public url: string;
  public identity;
  public token;

  constructor(private _http: Http) {
    this.url = GLOBAL.url;

  }

  //usamos signup para loguearnos
  signup(user_to_login){    
    /*Hacemos una petición ajax a nuestros métodos del API a la 
      URL que acaba en login*/
    
    let json = JSON.stringify(user_to_login);
    let params = "json="+json;
    let headers = new Headers({'Content-Type':'application/x-www-form-urlencoded'});
    
    return this._http.post(this.url+'/login', params, {headers: headers})
      .map(res => res.json());  
  }  
}
