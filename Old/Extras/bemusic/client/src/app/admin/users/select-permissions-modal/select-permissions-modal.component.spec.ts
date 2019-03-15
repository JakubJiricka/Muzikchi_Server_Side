import {KarmaTest} from "../../../../testing/karma-test";
import {SelectPermissionsModalComponent} from "./select-permissions-modal.component";
import {MapToIterable} from "../../shared/map-to-iterable";
import {UserService} from "../../admin/users/user.service";
import {ValueListsService} from "../../shared/value-lists.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";

describe('SelectPermissionsModalComponent', () => {
    let testBed: KarmaTest<SelectPermissionsModalComponent>;

    beforeEach(() => {
        testBed = new KarmaTest<SelectPermissionsModalComponent>({
            module: {
                declarations: [
                    SelectPermissionsModalComponent, MapToIterable,
                ],
                providers: [UserService, ValueListsService],
            },
            component: SelectPermissionsModalComponent
        });
    });

    it('shows modal', () => {
        spyOn(testBed.get(ValueListsService), 'getPermissions').and.returnValue(new BehaviorSubject(['foo']));

        testBed.component.show({});

        //fetches permissions list from backend
        expect(testBed.get(ValueListsService).getPermissions).toHaveBeenCalledTimes(1);

        //sets permissions on components
        expect(testBed.component.allPermissions).toEqual(['foo']);
    });

    it('renders permissions', () => {
        testBed.component.allPermissions = {group: ['foo', 'bar', 'baz']};
        testBed.component.disabledPermissions = ['bar'];
        testBed.component.selectedPermissions = ['baz'];
        testBed.fixture.detectChanges();

        //renders permissions group
        expect(testBed.find('.group-name').textContent.trim()).toEqual('group');

        //disables permission
        expect(testBed.findAll('.permission')[1].classList.contains('disabled')).toEqual(true);

        //renders permission name
        expect(testBed.find('.permission label').textContent.trim()).toEqual('foo');

        //toggles permission
        testBed.toggleCheckbox('.permission input');
        expect(testBed.component.selectedPermissions).toContain('foo');
        expect(testBed.component.selectedPermissions).toContain('baz');

        //checks checkbox of selected permission
        expect(testBed.findAll('.permission input')[2]['checked']).toEqual(true);
    });
});
