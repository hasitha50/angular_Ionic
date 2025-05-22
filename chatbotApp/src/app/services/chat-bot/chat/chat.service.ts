import { Injectable } from '@angular/core';
import { collection, collectionData, Firestore, query, where } from '@angular/fire/firestore';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from '../auth/auth.service';
import { addDoc, doc, getDoc, getDocs, onSnapshot, orderBy, serverTimestamp, setDoc, updateDoc } from 'firebase/firestore';

@Injectable({
  providedIn: 'root'
})
export class ChatService {
  constructor(private auth: AuthService, private firestore: Firestore) { }
  currentUserId: string | null = null;

  // Get current user ID
  getCurrentUserId(): string | null {
    return this.auth.getCurrentUserId();
  }

  // Get all users excluding the current user
  getUsers(): Observable<any[]> {
    this.currentUserId = this.getCurrentUserId();
    console.log(this.currentUserId )
    const usersRef = collection(this.firestore, 'users');

    return collectionData(usersRef, { idField: 'id' }).pipe(
      map(users => users.filter(user => user.id !== this.currentUserId ))
    );
  }

  async createChatRoom(user_id: string): Promise<any> {
    try {

    console.log(user_id);
      this.currentUserId = this.getCurrentUserId();  
      const chatRoomsRef = collection(this.firestore, 'chatRooms');
      const sortedMembers = [this.currentUserId, user_id].sort();
      const roomKey = sortedMembers.join('_');
  
      const q = query(chatRoomsRef,
        where('roomKey', '==', roomKey),
        where('type', '==', 'private')
      );
  
      const querySnapshot = await getDocs(q);
      let room;
  
      if (!querySnapshot.empty) {
        const docSnap = querySnapshot.docs[0];
        room = {
          id: docSnap.id,
          ...docSnap.data(),
        };
        console.log('Existing private chat room found:', room);
      } else {
        const room = {
          members: sortedMembers,  
          roomKey: sortedMembers.join('_'),
          type: 'private',
          createdAt: new Date(), 
          lastMessage: null,
          lastMessageTime: null,
        };
        
  
        await setDoc(doc(this.firestore, 'chatRooms', roomKey), room);
      
        console.log('New private chat room created:', room);
      }
  
      return room;
    } catch (error) {
      console.error('Error creating or retrieving chat room:', error);
      throw error;
    }
  }
  
  async getChatRooms(): Promise<{
    id: string;
    name: string;
    photo: string;
    lastMessage: string | null;
    lastMessageTime: any;
  }[]> {
    if (!this.currentUserId) {
      throw new Error('User not authenticated');
    }
  
    const chatRoomsRef = collection(this.firestore, 'chatRooms');
    const q = query(chatRoomsRef, where('members', 'array-contains', this.currentUserId));
    const querySnapshot = await getDocs(q);
  
    const rooms: {
      id: string;
      name: string;
      photo: string;
      lastMessage: string | null;
      lastMessageTime: any;
    }[] = [];
  
    for (const docSnap of querySnapshot.docs) {
      const roomData = docSnap.data() as any;
  
      if (roomData.type === 'private') {
        const otherUserId = roomData.members.find(
          (uid: string) => uid !== this.currentUserId
        );
  
        if (otherUserId) {
          const userDoc = await getDoc(doc(this.firestore, 'users', otherUserId));
          const userData = userDoc.data();
  
          if (userData) {
            rooms.push({
              id: docSnap.id,
              name: userData['name'],
              photo: userData['photo'] || 'assets/default-avatar.png',
              lastMessage: roomData['lastMessage'] || null,
              lastMessageTime: roomData['lastMessageTime'] || null,
            });
          }
        }
      }
    }
    return rooms;
  }
  
  getChatMessages(chatId: string, callback: (messages: any[]) => void) {
    const messagesRef = collection(this.firestore, `chatRooms/${chatId}/messages`);
    const q = query(messagesRef, orderBy('createdAt', 'asc'));
  
    const unsubscribe = onSnapshot(q, (snapshot) => {
      const messages = snapshot.docs.map((doc) => ({
        id: doc.id,
        ...doc.data(),
      }));
      callback(messages);
    });
  
    return unsubscribe; // useful if you want to stop listening later
  }

  async sendMessage(chatId: string, msg: string) {
    try {
      const newMessage = {
        message: msg,
        sender: this.currentUserId,
        createdAt: serverTimestamp(),
      };
  
      if (chatId) {
        const messagesRef = collection(this.firestore, `chatRooms/${chatId}/messages`);
        await addDoc(messagesRef, newMessage);

        await updateDoc(doc(this.firestore, 'chatRooms', chatId), {
          lastMessage: msg,
          lastMessageTime: serverTimestamp(),
        });
        
      }
    } catch (e) {
      console.error('Error creating message:', e);
      throw e;
    }
  }
  
}
