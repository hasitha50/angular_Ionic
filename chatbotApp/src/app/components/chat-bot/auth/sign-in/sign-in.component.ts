import { Component, CUSTOM_ELEMENTS_SCHEMA, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonicModule } from '@ionic/angular';
import { ReactiveFormsModule, FormBuilder, FormGroup } from '@angular/forms';
import { Auth } from '@angular/fire/auth';
import { signInWithEmailAndPassword } from 'firebase/auth';
import { doc, Firestore, getDoc, getFirestore } from 'firebase/firestore';
import { AuthService } from 'src/app/services/chat-bot/auth/auth.service';
 
@Component({
  selector: 'app-sign-in',
  standalone: true,
  imports: [CommonModule, IonicModule, ReactiveFormsModule],
  templateUrl: './sign-in.component.html',
  schemas:[CUSTOM_ELEMENTS_SCHEMA],
})
export class SignInComponent {
  form: FormGroup;
  @Input() isSignupForm: boolean = false; 
firestore = getFirestore();
  constructor(private fb: FormBuilder, private auth: Auth, private AuthService: AuthService) {
    this.form = this.fb.group({
      email: [''],
      password: ['']
    });
   
    
  }
  async signIn() {
    const { email, password } = this.form.value;
    try {
      if (this.form.valid) {
        await this.AuthService.signIn(this.form);
      }
  
      location.reload();
    } catch (err) {
      console.error('Sign-in error:', err);
    }
  }
  
}
