import {KarmaTest} from "../../../../testing/karma-test";
import {User} from "../../shared/models/User";
import {UserAccessManagerComponent} from "./user-access-manager.component";
import {MapToIterable} from "../../shared/map-to-iterable";
import {UserService} from "../../admin/users/user.service";
import {GroupService} from "../../admin/groups/group.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {ModalService} from "../../shared/modal/modal.service";
import {SelectGroupsModalComponent} from "../select-groups-modal/select-groups-modal.component";
import {SelectPermissionsModalComponent} from "../select-permissions-modal/select-permissions-modal.component";

describe('UserAccessManagerComponent', () => {
    let testBed: KarmaTest<any>;

    beforeEach(() => {
        testBed = new KarmaTest({
            module: {
                declarations: [
                    UserAccessManagerComponent, MapToIterable
                ],
                providers: [UserService, GroupService],
            },
            component: UserAccessManagerComponent,
        });
    });

    it('fetches groups on component init', () => {
        let groups = testBed.fake('Group', 3);
        testBed.component.user = testBed.fake('User');
        spyOn(testBed.get(GroupService), 'getGroups').and.returnValue(new BehaviorSubject({data: groups}));
        testBed.fixture.detectChanges();

        //calls backend
        expect(testBed.get(GroupService).getGroups).toHaveBeenCalledTimes(1);

        //sets groups on component
        expect(testBed.component.allGroups).toEqual(groups);
    });

    it('opens select groups modal', () => {
        spyOn(testBed.get(ModalService), 'show').and.returnValue({onDone: new BehaviorSubject([1,2,3])});
        spyOn(testBed.component, 'attachGroups');
        testBed.component.allGroups = testBed.fake('Group', 3);
        testBed.component.user = testBed.fake('User');
        testBed.fixture.detectChanges();

        testBed.find('.select-groups-modal-button').click();

        //opens modal with correct params
        expect(testBed.get(ModalService).show).toHaveBeenCalledWith(SelectGroupsModalComponent, {
            selected: [testBed.component.user.groups[0].id, testBed.component.user.groups[1].id]
        });

        //attaches groups to user
        expect(testBed.component.attachGroups).toHaveBeenCalledWith([1,2,3]);
    });

    it('attaches specified groups to user', () => {
        spyOn(testBed.get(UserService), 'attachGroups').and.returnValue(new BehaviorSubject({}));
        testBed.component.user = testBed.fake('User');
        testBed.component.allGroups = [testBed.fake('Group', 1, {id: 1}), testBed.fake('Group', 1, {id: 2})];

        testBed.component.attachGroups([1,2]);

        //calls backend
        expect(testBed.get(UserService).attachGroups).toHaveBeenCalledWith(testBed.component.user.id, {groups: [1,2]});

        //attaches groups to user model
        //TODO: uncomment after async/wait is supported in jasmine
        // expect(testBed.component.user.groups.length).toEqual(4);
        // expect(testBed.component.user.groups).toContain(testBed.component.allGroups[0]);
        // expect(testBed.component.user.groups).toContain(testBed.component.allGroups[1]);
    });

    it('detaches groups from user', () => {
        spyOn(testBed.get(UserService), 'detachGroups').and.returnValue(new BehaviorSubject({}));
        testBed.component.user = testBed.fake('User');
        let group = Object.assign({}, testBed.component.user.groups[1]);
        testBed.fixture.detectChanges();

        testBed.find('.detach-group-button').click();

        //calls backend
        expect(testBed.get(UserService).detachGroups).toHaveBeenCalledWith(testBed.component.user.id, {groups: [jasmine.any(Number)]});

        //detaches groups from user model
        //TODO: uncomment after async/wait is supported in jasmine
        //expect(testBed.component.user.groups).toEqual([group]);
    });

    it('opens select permissions modal', () => {
        spyOn(testBed.get(ModalService), 'show').and.returnValue({onDone: new BehaviorSubject([1,2,3])});
        spyOn(testBed.component, 'addPermissions');
        testBed.component.user = testBed.fake('User');
        testBed.fixture.detectChanges();

        testBed.find('.select-permissions-modal-button').click();

        //opens modal with correct params
        expect(testBed.get(ModalService).show).toHaveBeenCalledWith(SelectPermissionsModalComponent);

        //attaches permissions to user
        expect(testBed.component.addPermissions).toHaveBeenCalledWith([1,2,3]);
    });

    it('adds specified permissions to user model', () => {
        spyOn(testBed.get(UserService), 'addPermissions').and.returnValue(new BehaviorSubject({data: {permissions: 'response'}}));
        testBed.component.user = testBed.fake('User');

        testBed.component.addPermissions(['foo', 'bar']);

        //calls backend
        expect(testBed.get(UserService).addPermissions).toHaveBeenCalledWith(testBed.component.user.id, {permissions: ['foo', 'bar']});

        //overrides user permissions with response from backend
        //TODO: uncomment after async/wait is supported in jasmine
        //expect(testBed.component.user.permissions).toEqual('response');
    });

    it('removes specified permissions from user model', () => {
        spyOn(testBed.get(UserService), 'removePermissions').and.returnValue(new BehaviorSubject({data: {permissions: 'response'}}));
        testBed.component.user = testBed.fake('User', 1, {permissions: {foo: 1, bar: 0}});
        testBed.fixture.detectChanges();

        testBed.find('.remove-permission-button').click();

        //calls backend
        expect(testBed.get(UserService).removePermissions).toHaveBeenCalledWith(testBed.component.user.id, {permissions: ['foo']});

        //overrides user permissions with response from backend
        //TODO: uncomment after async/wait is supported in jasmine
        //expect(testBed.component.user.permissions).toEqual('response');
    });

    it('renders groups attached to user', () => {
        testBed.component.user = testBed.fake('User', 1, {permissions: {foo: 1, bar: 0}});
        testBed.fixture.detectChanges();

        //renders groups
        expect(testBed.findAll('.group').length).toEqual(testBed.component.user.groups.length);
        expect(testBed.find('.group').textContent.trim()).toEqual(testBed.component.user.groups[0].name);

        //renders permissions
        expect(testBed.findAll('.permission').length).toEqual(2);
        expect(testBed.find('.permission').textContent.trim()).toEqual('foo');
    });
});
